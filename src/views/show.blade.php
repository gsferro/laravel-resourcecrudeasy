{{-- recebe os dados que devem ser exibidos como texto --}}
{{-- {{ dd($fields, $model) }} --}}
{{--<div class="col-xs-12 col-sm-offset-2 col-sm-8 col-md-offset-2 col-md-8">--}}
	<div class="col-xs-12">
		<form class="form-horizontal">
			@box([ 'noPadding' => 'true', 'btnBox'=>'false'])
				@forelse($fields as $field => $label)
					@if (is_array($label))
						@foreach ($label as $rel => $lb)
							@if ($rel == "_callback")
								@if (!empty(call_user_func([$model, $lb["method"]])))
									@p([
										"label" => $lb["label"],
										"slot"  => call_user_func([$model, $lb["method"]]) ?? ""
									])
								@endif
							@else
								@if (!empty($model->$field->$rel))
									@p([ "label" => $lb, "slot" => $model->$field->$rel ?? "" ])
								@endif
							@endif
						@endforeach
					@else
						@if (!empty($model->$field))
							@if($field == "foto_exemplar" || $field == "arquivo")
								<label class="control-label col-sm-4" style="color: #333;font-family: Rawline;font-size: 16px;font-style: normal;font-weight: 600;line-height: normal;">{{$label}}:</label>
								<p class="form-control-static" style="min-height: 34px;padding-top: 7px;padding-bottom: 7px;margin-bottom: 0;">
									<a class="br-button is-primary" style="min-width:70px;width:auto;height: 32px;" target="_blank" href="{{$model->$field}}">
										Visualizar
									</a>
								</p>
							@else
								@p([ "label" => $label, "slot" => strip_tags($model->$field)  ?? "" ])
							@endif
						@endif
					@endif
				@empty
				@endforelse
				{{-- @if(isset($relations))
					@forelse($relations as $key => $relation)
						<hr>
						<div class="row text-center">
							<h5 class="text-center col-12">{{ $key }}</h5>
							@if($relation["relation_rows"]=="multiple")
								@foreach($relation["list"] as $item)
									<i style="display: none;">
										{{ $column         = $relation["column"] }}
										{{ $alias_column   = $relation["alias_column"] }}
									</i>
									<div class="col-12 text-left">
										<label>{{ $alias_column }}: </label>
										{{ $item->$column ?? "Não existe" }}
									</div>
								@endforeach
							@elseif($relation["relation_rows"]=="unique")
								<i style="display: none;">
									{{ $column         = $relation["column"] }}
									{{ $alias_column   = $relation["alias_column"] }}
								</i>
								<div class="col-12 text-left">
									<label>{{ $alias_column }}: </label>
									{{ $relation["list"]->$column ?? "Não existe" }}
								</div>
							@endif
						</div>
					@empty
					@endforelse
				@endif --}}
			@endbox
		</form>
	</div>